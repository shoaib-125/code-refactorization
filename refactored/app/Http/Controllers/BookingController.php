<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use DTApi\Repository\UserRepository;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;
    protected $userRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository, UserRepository $userRepository)
    {
        $this->repository = $bookingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user = $this->userRepository->getUserById( $request->get('user_id') )) {
            $page = isset($request->page) ? $request->page : 0;

            $response = $this->repository->getUsersJobs($user, ['immediate' => 'yes', 'job_type' => 'new'], $page);
            $response['user'] = $user;
            $response['usertype'] = 'basic';

            if ( $user->is('customer') ) $response['usertype'] = 'customer';
            elseif ( $user->is('translator') ) $response['usertype'] = 'translator';


        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') ||
                    $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param BookingRequest $request
     * @return mixed
     */
    public function store(BookingRequest $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param $id
     * @param BookingRequest $request
     * @return mixed
     */
    public function update($id, BookingRequest $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user = $this->userRepository->find($request->get('user_id'))) {
            $ePage = isset($request->epage) ? $request->epage : 0;
            $nPage = isset($request->npage) ? $request->npage : 0;

            $page = isset($request->page) ? $request->page : 0;

            $response = $this->repository->getUsersJobs($user, ['job_type' => 'historic'], $page);
            $response['user'] = $user;

            if ($user->is('customer')) $response['usertype'] = 'customer';
            elseif ($user->is('translator')) $response['usertype'] = 'translator';
            else $response['usertype'] = 'basic';

            return response($response);
        }
        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data['job_id'], $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = !empty($data['distance']) ? $data['distance'] : "";
        $time = !empty($data['time']) ? $data['time'] : "";

        if ( !empty($data['jobid']) ) {
            $jobId = $data['jobid'];
        }

        $session = !empty($data['session_time']) ? $data['session_time'] : "";

        if( $data['flagged'] && empty($data['admincomment']) ) {}

        $flagged = 'no';
        if ( $data['flagged'] == 'true') {
            if ($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        }

        $manuallyHandled = 'no';
        if ($data['manually_handled'] == 'true') {
            $manuallyHandled = 'yes';
        }

        $byAdmin = 'no';
        if ($data['by_admin'] == 'true') {
            $byAdmin = 'yes';
        }

        $adminComment = !empty($data['admincomment']) ? $data['admincomment'] : "";

        if ( ($time || $distance) && !empty($jobId) ) {

            Distance::where('job_id', '=', $jobId)->update(array('distance' => $distance, 'time' => $time));
        }

        if ( ($adminComment || $session || $flagged || $manuallyHandled || $byAdmin) && !empty($jobId) ) {

            Job::where('id', '=', $jobId)->update(array('admin_comments' => $adminComment, 'flagged'
            => $flagged, 'session_time' => $session, 'manually_handled' => $manuallyHandled, 'by_admin' => $byAdmin));

        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
